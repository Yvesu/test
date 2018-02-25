import React, { Component } from 'react'
import { Button, Menu, Dropdown, Icon} from 'antd'
//下拉菜单
class SortDisableMenu extends Component {
  constructor(props){
    super(props)
     this.state={
       visible:false
     }
  }
  render() {
    return (
      <Dropdown overlay={
        <Menu className="dropMenu" style={{textAlign:"center"}}>
          <Menu.Item  key="1" >
            <Button type="primary">启用</Button>
          </Menu.Item>
          <Menu.Item  key="2" >
            <Button type="danger">删除</Button>
          </Menu.Item>
        </Menu>}
        trigger={['click']}>
              <Button type={this.props.operation ? "" : "primary"} >
                  {this.props.operation ? "操作" : "批量操作"}
                    <Icon type="down" />
              </Button>
      </Dropdown>
      )
  }

}

export default SortDisableMenu
