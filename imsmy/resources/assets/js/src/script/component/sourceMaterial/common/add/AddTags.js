import React, { Component } from 'react'

import { Tag, Input, Tooltip, Button } from 'antd'
import Fetch from 'utils/fetch'
class AddTags extends Component {
  constructor(props){
    super(props)
    this.state = {
      tags: [],
      inputVisible: false,
      inputValue: ''
    }
  }
  handleClose = (removedTag) => {
    const tags = this.state.tags.filter(tag => tag !== removedTag)
    // console.log(tags)
    this.setState({ tags })
    this.props.onChange(tags)
  }

  showInput = () => {
    this.setState({ inputVisible: true }, () => this.input.focus())
  }

  handleInputChange = (e) => {
    this.setState({ inputValue: e.target.value })
  }

  handleInputConfirm = () => {
    const state = this.state
    const inputValue = state.inputValue
    let tags = state.tags
    if (inputValue && tags.indexOf(inputValue) === -1) {
      tags = [...tags, inputValue]
    }
    this.setState({
      tags,
      inputVisible: false,
      inputValue: '',
    })
    this.props.onChange(tags)
  }
  saveInputRef = input => this.input = input
  //
  // resolveKeyword=(data)=>{
  //   var obj =data
  //   var Keyword = []
  //   // console.log(data,'data');
  //   // console.log(Keyword,'key');
  //   for(var i in obj){
  //     Keyword.push(obj[i])
  //   }
  //   this.setState({
  //     tags:Keyword
  //   })
  //
  // }


  render(){
    const { tags, inputVisible, inputValue } = this.state
      return (
        <div>
          {tags.map((tag, index) => {
            const isLongTag = tag.length > 20
            const tagElem = (
              <Tag key={tag} closable={true} afterClose={() => this.handleClose(tag)}>
                {isLongTag ? `${tag.slice(0, 20)}...` : tag}
              </Tag>
            )
            return isLongTag ? <Tooltip title={tag}>{tagElem}</Tooltip> : tagElem
          })}
          {inputVisible && (
            <Input
              ref={this.saveInputRef}
              type="text"
              size="small"
              style={{ width: "100%" }}
              value={inputValue}
              onChange={this.handleInputChange}
              onBlur={this.handleInputConfirm}
              onPressEnter={this.handleInputConfirm}
            />
          )}
          {!inputVisible && <Button type="primary" size="small" onClick={this.showInput}>添加</Button>}
        </div>
      )
  }
  componentDidMount(){
    // console.log(this.props.test,'shiyis');
  }
  componentWillReceiveProps(nextPorps){
    if(nextPorps.newKeyWords.length !==0){
      this.setState({
        tags:nextPorps.newKeyWords
      })
    }
  }

}

export default AddTags
