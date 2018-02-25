import React, { Component } from 'react'

import { Tag, Input, Tooltip, Button } from 'antd'
import FetchPost from 'utils/fetch'
class Labels extends Component {
  constructor(props){
    super(props)
    this.state = {
      tags: ['飞雪连天射白鹿，笑书神侠倚碧鸳 - 越女剑','飞狐外传', '雪山飞狐', '连城诀','天龙八部','射雕英雄传',
      '白马啸西风', '鹿鼎记', '笑傲江湖','书剑恩仇录','神雕侠侣',
      '侠客行', '倚天屠龙记', '碧血剑','鸳鸯刀','越女剑'],
      inputVisible: false,
      inputValue: ''
    }
  }
  handleClose = (removedTag) => {
    const tags = this.state.tags.filter(tag => tag !== removedTag)
    console.log(tags)
    this.setState({ tags })
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
  }
  saveInputRef = input => this.input = input
  render(){
    const { tags, inputVisible, inputValue } = this.state
      return (
        <div style={{padding:"10px 15px"}}>
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
          {!inputVisible && <Button type="primary" size="small" onClick={this.showInput}>添加热搜词</Button>}
        </div>
      )
  }

}

export default Labels
