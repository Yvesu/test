import React, { Component } from 'react'

//未通过  -  引入一个tag标签 进行各种实验
import { Tag,Badge, Input, Tooltip, Button } from 'antd'

class NotPass extends Component {
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
    console.log(tags)
    this.setState({
      tags,
      inputVisible: false,
      inputValue: '',
    })
  }
  saveInputRef = input => this.input = input
  render(){
    const { tags, inputVisible, inputValue } = this.state
    // console.log(this.isLongTag);
      return (
        <div style={{padding:"10px 15px",border:"1px solid #000",background:"#fff",minHeight:"200px"}}>
          <p style={{marginBottom:"20px",height:"20px",border:"1px solid #ccc",textAlign:"center"}}>H3</p>
            <p style={{marginBottom:"10px"}}>

            </p>
          {tags.map((tag, index) => {
            //看单个标签里边长度有没有超过20
            const isLongTag = tag.length > 10
            // console.log(isLongTag);
            const tagElem = (
              //这个就是显示到页面上的标签  样式什么的都是通过这个来修改
              <Tag key={tag} closable={true} afterClose={() => this.handleClose(tag)}>
                {isLongTag ? `${tag.slice(0, 10)}...` : tag}
              </Tag>
            )                  //Tooltip这个的作用就是在标签超过规定的数值后 鼠标指向它 浮现标签内容
            return isLongTag ? <Tooltip title={tag}>{tagElem}</Tooltip> : tagElem
          })}
          {inputVisible && (
            <Input
              ref={this.saveInputRef}
              type="text"
              size="small"
              style={{ minWidth: 100 }}
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


export default NotPass
